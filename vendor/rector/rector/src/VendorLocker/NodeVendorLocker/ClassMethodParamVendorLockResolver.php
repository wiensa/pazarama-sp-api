<?php

declare (strict_types=1);
namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Rector\FileSystem\FilePathHelper;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Reflection\ReflectionResolver;
final class ClassMethodParamVendorLockResolver
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\FileSystem\FilePathHelper
     */
    private $filePathHelper;
    public function __construct(NodeNameResolver $nodeNameResolver, FamilyRelationsAnalyzer $familyRelationsAnalyzer, ReflectionResolver $reflectionResolver, FilePathHelper $filePathHelper)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
        $this->filePathHelper = $filePathHelper;
    }
    public function isVendorLocked(ClassMethod $classMethod) : bool
    {
        if ($classMethod->isMagic()) {
            return \true;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($classMethod);
        if ($this->hasTraitMethodVendorLock($classReflection, $methodName)) {
            return \true;
        }
        // has interface vendor lock? → better skip it, as PHPStan has access only to just analyzed classes
        if ($this->hasParentInterfaceMethod($classReflection, $methodName)) {
            return \true;
        }
        return $this->hasClassMethodLockMatchingFileName($classReflection, $methodName, '/vendor/');
    }
    private function hasTraitMethodVendorLock(ClassReflection $classReflection, string $methodName) : bool
    {
        $relatedReflectionClasses = $this->familyRelationsAnalyzer->getChildrenOfClassReflection($classReflection);
        foreach ($relatedReflectionClasses as $relatedReflectionClass) {
            foreach ($relatedReflectionClass->getTraits() as $traitReflectionClass) {
                /** @var ClassReflection $traitReflectionClass */
                if ($traitReflectionClass->hasMethod($methodName)) {
                    return \true;
                }
            }
        }
        return \false;
    }
    /**
     * Has interface even in our project?
     * Better skip it, as PHPStan has access only to just analyzed classes.
     * This might change type, that works for current class, but breaks another implementer.
     */
    private function hasParentInterfaceMethod(ClassReflection $classReflection, string $methodName) : bool
    {
        foreach ($classReflection->getInterfaces() as $interfaceClassReflection) {
            if ($interfaceClassReflection->hasMethod($methodName)) {
                return \true;
            }
        }
        return \false;
    }
    private function hasClassMethodLockMatchingFileName(ClassReflection $classReflection, string $methodName, string $filePathPartName) : bool
    {
        $ancestorClassReflections = \array_merge($classReflection->getParents(), $classReflection->getInterfaces());
        foreach ($ancestorClassReflections as $ancestorClassReflection) {
            // parent type
            if (!$ancestorClassReflection->hasNativeMethod($methodName)) {
                continue;
            }
            // is file in vendor?
            $fileName = $ancestorClassReflection->getFileName();
            // probably internal class
            if ($fileName === null) {
                continue;
            }
            // not conditions? its a match
            if ($filePathPartName === '') {
                return \true;
            }
            $normalizedFileName = $this->filePathHelper->normalizePathAndSchema($fileName);
            if (\strpos($normalizedFileName, $filePathPartName) !== \false) {
                return \true;
            }
        }
        return \false;
    }
}
